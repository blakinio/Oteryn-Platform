#!/usr/bin/env python3
"""Read-only, sanitized Cloudflare edge audit for Oteryn public hostnames."""
from __future__ import annotations

import json
import os
import re
import sys
import urllib.error
import urllib.request
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

API_BASE = os.environ.get("CLOUDFLARE_API_BASE_URL", "https://api.cloudflare.com/client/v4").rstrip("/")
TOKEN = os.environ.get("CLOUDFLARE_API_TOKEN", "")
ACCOUNT_ID = os.environ.get("CLOUDFLARE_ACCOUNT_ID", "")
ZONE_ID = os.environ.get("CLOUDFLARE_ZONE_ID", "")
WWW_HOST = "oteryn.molehill.cloud"
LOGIN_HOST = "login.oteryn.molehill.cloud"
OUT = Path(os.environ.get("CLOUDFLARE_EDGE_AUDIT_OUT", "cloudflare-edge-audit"))
RELEVANT_PHASES = {
    "http_request_dynamic_redirect",
    "http_request_firewall_custom",
    "http_response_headers_transform",
    "http_config_settings",
}


def fail(message: str) -> None:
    print(f"ERROR: {message}", file=sys.stderr)
    raise SystemExit(1)


def validate() -> None:
    if not TOKEN:
        fail("CLOUDFLARE_API_TOKEN is missing")
    if not re.fullmatch(r"[0-9a-fA-F]{32}", ACCOUNT_ID):
        fail("CLOUDFLARE_ACCOUNT_ID must be a 32-character hexadecimal identifier")
    if not re.fullmatch(r"[0-9a-fA-F]{32}", ZONE_ID):
        fail("CLOUDFLARE_ZONE_ID must be a 32-character hexadecimal identifier")


def api_get(path: str) -> dict[str, Any]:
    request = urllib.request.Request(
        f"{API_BASE}{path}",
        headers={"Authorization": f"Bearer {TOKEN}", "Accept": "application/json"},
        method="GET",
    )
    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            status = response.status
            raw = response.read(2_000_000)
    except urllib.error.HTTPError as exc:
        status = exc.code
        raw = exc.read(2_000_000)
    except Exception as exc:
        return {"http_status": 0, "readable": False, "error": f"{type(exc).__name__}: {exc}"}

    try:
        payload = json.loads(raw.decode("utf-8"))
    except Exception:
        return {"http_status": status, "readable": False, "error": "non-JSON response"}

    errors = []
    for item in payload.get("errors", []) if isinstance(payload, dict) else []:
        if isinstance(item, dict):
            errors.append({"code": item.get("code"), "message": str(item.get("message", ""))[:300]})
    return {
        "http_status": status,
        "readable": 200 <= status < 300 and isinstance(payload, dict) and payload.get("success") is True,
        "result": payload.get("result") if isinstance(payload, dict) else None,
        "errors": errors,
    }


def endpoint_state(response: dict[str, Any]) -> str:
    if response.get("readable"):
        return "readable"
    if response.get("http_status") in (401, 403):
        return "permission_denied"
    if response.get("http_status") == 404:
        return "not_found_or_unavailable"
    return "error"


def certificate_summary(response: dict[str, Any]) -> dict[str, Any]:
    summary: dict[str, Any] = {"state": endpoint_state(response), "http_status": response.get("http_status")}
    if not response.get("readable"):
        summary["errors"] = response.get("errors", [])
        return summary
    packs = response.get("result") if isinstance(response.get("result"), list) else []
    matching = []
    active = False
    for pack in packs:
        if not isinstance(pack, dict):
            continue
        hosts = [str(value).lower() for value in pack.get("hosts", []) if isinstance(value, str)]
        if LOGIN_HOST in hosts:
            status = str(pack.get("status", "unknown"))
            matching.append(
                {
                    "id": pack.get("id"),
                    "type": pack.get("type"),
                    "status": status,
                    "host_count": len(hosts),
                    "exact_login_covered": True,
                }
            )
            active = active or status.lower() == "active"
    summary.update({"pack_count": len(packs), "matching_packs": matching, "active_exact_login_coverage": active})
    return summary


def setting_summary(name: str, response: dict[str, Any]) -> dict[str, Any]:
    item = {"state": endpoint_state(response), "http_status": response.get("http_status")}
    if response.get("readable") and isinstance(response.get("result"), dict):
        result = response["result"]
        item.update({"id": result.get("id", name), "value": result.get("value"), "editable": result.get("editable")})
    else:
        item["errors"] = response.get("errors", [])
    return item


def ruleset_summary(list_response: dict[str, Any]) -> tuple[dict[str, Any], list[dict[str, Any]]]:
    summary: dict[str, Any] = {"state": endpoint_state(list_response), "http_status": list_response.get("http_status")}
    if not list_response.get("readable"):
        summary["errors"] = list_response.get("errors", [])
        return summary, []
    result = list_response.get("result") if isinstance(list_response.get("result"), list) else []
    selected = []
    for ruleset in result:
        if not isinstance(ruleset, dict) or ruleset.get("phase") not in RELEVANT_PHASES:
            continue
        selected.append(
            {
                "id": ruleset.get("id"),
                "phase": ruleset.get("phase"),
                "kind": ruleset.get("kind"),
                "name": str(ruleset.get("name", ""))[:160],
            }
        )
    summary["relevant_rulesets"] = selected
    return summary, selected


def rules_detail_summary(ruleset: dict[str, Any], response: dict[str, Any]) -> dict[str, Any]:
    item = {
        "id": ruleset.get("id"),
        "phase": ruleset.get("phase"),
        "state": endpoint_state(response),
        "http_status": response.get("http_status"),
    }
    if not response.get("readable") or not isinstance(response.get("result"), dict):
        item["errors"] = response.get("errors", [])
        return item
    rules = response["result"].get("rules", [])
    matches = []
    for rule in rules if isinstance(rules, list) else []:
        if not isinstance(rule, dict):
            continue
        expression = str(rule.get("expression", ""))
        if WWW_HOST not in expression and LOGIN_HOST not in expression:
            continue
        matches.append(
            {
                "id": rule.get("id"),
                "ref": rule.get("ref"),
                "action": rule.get("action"),
                "enabled": rule.get("enabled", True),
                "matches_www": WWW_HOST in expression,
                "matches_login": LOGIN_HOST in expression,
            }
        )
    item.update({"rule_count": len(rules) if isinstance(rules, list) else 0, "oteryn_matching_rules": matches})
    return item


def bot_summary(response: dict[str, Any]) -> dict[str, Any]:
    item = {"state": endpoint_state(response), "http_status": response.get("http_status")}
    if not response.get("readable") or not isinstance(response.get("result"), dict):
        item["errors"] = response.get("errors", [])
        return item
    result = response["result"]
    allowed = (
        "fight_mode",
        "sbfm_likely_automated",
        "sbfm_definitely_automated",
        "sbfm_verified_bots",
        "sbfm_static_resource_protection",
        "enable_js",
    )
    item["settings"] = {key: result.get(key) for key in allowed if key in result}
    stale = result.get("stale_zone_configuration")
    if isinstance(stale, dict):
        item["stale_zone_configuration"] = {key: stale.get(key) for key in allowed if key in stale}
    return item


def access_summary(response: dict[str, Any]) -> dict[str, Any]:
    item = {"state": endpoint_state(response), "http_status": response.get("http_status")}
    if not response.get("readable"):
        item["errors"] = response.get("errors", [])
        return item
    apps = response.get("result") if isinstance(response.get("result"), list) else []
    matches = []
    for app in apps:
        if not isinstance(app, dict):
            continue
        domain = str(app.get("domain", "")).lower()
        if domain in (WWW_HOST, LOGIN_HOST) or domain.startswith(f"{WWW_HOST}/") or domain.startswith(f"{LOGIN_HOST}/"):
            matches.append({"id": app.get("id"), "domain": domain, "type": app.get("type")})
    item.update({"application_count": len(apps), "oteryn_applications": matches})
    return item


def main() -> None:
    validate()
    OUT.mkdir(parents=True, exist_ok=True)
    token_path = f"/accounts/{ACCOUNT_ID}/tokens/verify" if TOKEN.startswith("cfat_") else "/user/tokens/verify"
    token = api_get(token_path)
    if not token.get("readable") or not isinstance(token.get("result"), dict) or token["result"].get("status") != "active":
        fail(f"Cloudflare token verification failed with HTTP {token.get('http_status')}")

    certs = api_get(f"/zones/{ZONE_ID}/ssl/certificate_packs?status=all&per_page=100")
    setting_names = ("always_use_https", "min_tls_version", "security_level", "browser_check", "security_header")
    settings = {name: setting_summary(name, api_get(f"/zones/{ZONE_ID}/settings/{name}")) for name in setting_names}

    rulesets_response = api_get(f"/zones/{ZONE_ID}/rulesets")
    rulesets, selected = ruleset_summary(rulesets_response)
    details = []
    for ruleset in selected:
        ruleset_id = ruleset.get("id")
        if ruleset_id:
            details.append(rules_detail_summary(ruleset, api_get(f"/zones/{ZONE_ID}/rulesets/{ruleset_id}")))

    evidence = {
        "observed_at_utc": datetime.now(timezone.utc).isoformat(),
        "classification": "READ_ONLY_CLOUDFLARE_EDGE_AUDIT",
        "canonical_hosts": [WWW_HOST, LOGIN_HOST],
        "token": {"active": True, "verification_scope": "account" if TOKEN.startswith("cfat_") else "user"},
        "certificate_packs": certificate_summary(certs),
        "zone_settings": settings,
        "rulesets": rulesets,
        "ruleset_details": details,
        "bot_management": bot_summary(api_get(f"/zones/{ZONE_ID}/bot_management")),
        "access_applications": access_summary(api_get(f"/accounts/{ACCOUNT_ID}/access/apps?per_page=100")),
        "mutation": "none",
    }
    (OUT / "evidence.json").write_text(json.dumps(evidence, indent=2, sort_keys=True) + "\n", encoding="utf-8")

    lines = [
        "# Cloudflare Oteryn edge audit",
        "",
        f"Observed at: `{evidence['observed_at_utc']}`",
        "",
        f"- certificate_packs: `{evidence['certificate_packs']['state']}`; active exact login coverage: `{evidence['certificate_packs'].get('active_exact_login_coverage', 'unknown')}`",
        f"- rulesets: `{evidence['rulesets']['state']}`; relevant count: `{len(evidence['rulesets'].get('relevant_rulesets', []))}`",
        f"- bot_management: `{evidence['bot_management']['state']}`",
        f"- access_applications: `{evidence['access_applications']['state']}`",
    ]
    for name, item in settings.items():
        lines.append(f"- zone setting `{name}`: `{item['state']}`; value: `{item.get('value', 'unknown')}`")
    lines.extend(["", "This audit performs GET requests only and writes a sanitized artifact.", ""])
    summary = "\n".join(lines)
    (OUT / "summary.md").write_text(summary, encoding="utf-8")
    print(summary)
    github_summary = os.environ.get("GITHUB_STEP_SUMMARY")
    if github_summary:
        with open(github_summary, "a", encoding="utf-8") as handle:
            handle.write(summary)


if __name__ == "__main__":
    main()

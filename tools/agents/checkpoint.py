#!/usr/bin/env python3
from __future__ import annotations

import argparse
import json
import re
import sys
from dataclasses import dataclass
from pathlib import Path
from typing import Iterable

CHECKPOINT_HEADING = "## Context checkpoint"
DEFAULT_CONTRACT = Path("docs/agents/GOVERNANCE_CONTRACT.json")
PLACEHOLDER_NEXT_ACTIONS = {"none", "unknown", "pending", "n/a", "tbd", "todo", "later"}

SCALAR_KEYS = {
    "checkpoint_version",
    "updated_at",
    "head",
    "branch",
    "pr",
    "status",
    "next_action",
}
LIST_KEYS = {
    "context_routes",
    "owned_paths",
    "proven",
    "derived",
    "unknown",
    "conflicts",
    "rejected_hypotheses",
    "changed_paths",
    "blockers",
}
MAP_KEYS = {"first_failure"}
LIST_OF_MAP_KEYS = {"validation"}


class CheckpointError(ValueError):
    pass


@dataclass(frozen=True)
class GovernanceContract:
    checkpoint_version: str
    required_fields: frozenset[str]
    allowed_statuses: frozenset[str]
    allowed_validation_results: frozenset[str]
    evidence_fields: tuple[str, ...]


@dataclass(frozen=True)
class ParsedCheckpoint:
    data: dict[str, object]
    block: str
    source: Path


def repository_root() -> Path:
    return Path(__file__).resolve().parents[2]


def load_contract(path: Path | None = None) -> GovernanceContract:
    contract_path = path or repository_root() / DEFAULT_CONTRACT
    try:
        raw = json.loads(contract_path.read_text(encoding="utf-8"))
        shared = raw["shared_checkpoint_contract"]
        evidence_state_fields = shared["evidence_state_fields"]
    except (OSError, json.JSONDecodeError, KeyError, TypeError) as exc:
        raise CheckpointError(f"{contract_path}: invalid governance contract: {exc}") from exc

    version = str(shared.get("version", "")).strip()
    required = shared.get("required_fields")
    statuses = shared.get("allowed_statuses")
    results = shared.get("allowed_validation_results")

    if not version:
        raise CheckpointError(f"{contract_path}: shared checkpoint contract version is empty")
    if not isinstance(required, list) or not all(isinstance(item, str) for item in required):
        raise CheckpointError(f"{contract_path}: required_fields must be a list of strings")
    if not isinstance(statuses, list) or not all(isinstance(item, str) for item in statuses):
        raise CheckpointError(f"{contract_path}: allowed_statuses must be a list of strings")
    if not isinstance(results, list) or not all(isinstance(item, str) for item in results):
        raise CheckpointError(
            f"{contract_path}: allowed_validation_results must be a list of strings"
        )
    if not isinstance(evidence_state_fields, dict):
        raise CheckpointError(f"{contract_path}: evidence_state_fields must be an object")

    expected_states = {"PROVEN", "DERIVED", "UNKNOWN", "CONFLICT"}
    if set(evidence_state_fields) != expected_states:
        raise CheckpointError(
            f"{contract_path}: evidence_state_fields must define exactly "
            f"{', '.join(sorted(expected_states))}"
        )
    evidence_fields = tuple(str(evidence_state_fields[state]) for state in sorted(expected_states))

    parser_supported = SCALAR_KEYS | LIST_KEYS | MAP_KEYS | LIST_OF_MAP_KEYS
    unsupported = sorted(set(required) - parser_supported)
    if unsupported:
        raise CheckpointError(
            f"{contract_path}: validator does not support required fields: {', '.join(unsupported)}"
        )
    for field in evidence_fields:
        if field not in LIST_KEYS:
            raise CheckpointError(
                f"{contract_path}: evidence state field {field!r} must be a supported list field"
            )

    return GovernanceContract(
        checkpoint_version=version,
        required_fields=frozenset(required),
        allowed_statuses=frozenset(statuses),
        allowed_validation_results=frozenset(results),
        evidence_fields=evidence_fields,
    )


def _scalar(value: str) -> str:
    value = value.strip()
    if len(value) >= 2 and value[0] == value[-1] and value[0] in {'"', "'"}:
        return value[1:-1]
    return value


def extract_checkpoint_block(text: str, *, source: Path | None = None) -> str | None:
    heading_matches = list(re.finditer(r"(?m)^## Context checkpoint\s*$", text))
    location = str(source) if source else "<text>"
    if not heading_matches:
        return None
    if len(heading_matches) != 1:
        raise CheckpointError(
            f"{location}: expected exactly one {CHECKPOINT_HEADING} section; "
            f"found {len(heading_matches)}"
        )

    heading = heading_matches[0]
    remainder = text[heading.end() :]
    next_heading = re.search(r"(?m)^##\s+", remainder)
    section = remainder[: next_heading.start()] if next_heading else remainder
    fences = list(re.finditer(r"```(?:yaml|yml)\s*\n", section, flags=re.IGNORECASE))
    if not fences:
        raise CheckpointError(f"{location}: context checkpoint heading has no fenced YAML block")
    if len(fences) != 1:
        raise CheckpointError(
            f"{location}: context checkpoint section must contain exactly one fenced YAML block"
        )

    fence = fences[0]
    block_start = fence.end()
    block_end = section.find("```", block_start)
    if block_end < 0:
        raise CheckpointError(f"{location}: context checkpoint fenced block is not closed")
    return section[block_start:block_end].strip("\n")


def parse_checkpoint_block(block: str, *, source: Path | None = None) -> dict[str, object]:
    data: dict[str, object] = {}
    seen_top_level: set[str] = set()
    current_key: str | None = None
    current_validation: dict[str, str] | None = None
    location = str(source) if source else "<checkpoint>"

    for lineno, raw in enumerate(block.splitlines(), start=1):
        if not raw.strip() or raw.lstrip().startswith("#"):
            continue

        indent = len(raw) - len(raw.lstrip(" "))
        stripped = raw.strip()

        if indent == 0:
            if ":" not in stripped:
                raise CheckpointError(f"{location}:{lineno}: invalid top-level checkpoint line")
            key, value = stripped.split(":", 1)
            key = key.strip()
            value = value.strip()
            if key in seen_top_level:
                raise CheckpointError(f"{location}:{lineno}: duplicate top-level key {key!r}")
            seen_top_level.add(key)
            current_key = key
            current_validation = None

            if key in LIST_KEYS:
                if value not in {"", "[]"}:
                    raise CheckpointError(f"{location}:{lineno}: {key} must be a YAML list")
                data[key] = []
            elif key in MAP_KEYS:
                if value:
                    raise CheckpointError(f"{location}:{lineno}: {key} must be a YAML mapping")
                data[key] = {}
            elif key in LIST_OF_MAP_KEYS:
                if value not in {"", "[]"}:
                    raise CheckpointError(f"{location}:{lineno}: {key} must be a YAML list")
                data[key] = []
            else:
                data[key] = _scalar(value)
            continue

        if current_key is None:
            raise CheckpointError(f"{location}:{lineno}: nested checkpoint value has no parent key")

        if current_key in LIST_KEYS:
            if indent != 2 or not stripped.startswith("- "):
                raise CheckpointError(f"{location}:{lineno}: invalid list item under {current_key}")
            values = data[current_key]
            assert isinstance(values, list)
            values.append(_scalar(stripped[2:].strip()))
            continue

        if current_key in MAP_KEYS:
            if indent != 2 or ":" not in stripped:
                raise CheckpointError(f"{location}:{lineno}: invalid mapping item under {current_key}")
            key, value = stripped.split(":", 1)
            mapping = data[current_key]
            assert isinstance(mapping, dict)
            nested_key = key.strip()
            if nested_key in mapping:
                raise CheckpointError(
                    f"{location}:{lineno}: duplicate key {current_key}.{nested_key}"
                )
            mapping[nested_key] = _scalar(value)
            continue

        if current_key in LIST_OF_MAP_KEYS:
            items = data[current_key]
            assert isinstance(items, list)
            if indent == 2 and stripped.startswith("- "):
                item_text = stripped[2:].strip()
                if ":" not in item_text:
                    raise CheckpointError(
                        f"{location}:{lineno}: validation item must start with a key/value pair"
                    )
                key, value = item_text.split(":", 1)
                current_validation = {key.strip(): _scalar(value)}
                items.append(current_validation)
                continue
            if indent == 4 and current_validation is not None and ":" in stripped:
                key, value = stripped.split(":", 1)
                nested_key = key.strip()
                if nested_key in current_validation:
                    raise CheckpointError(
                        f"{location}:{lineno}: duplicate validation field {nested_key!r}"
                    )
                current_validation[nested_key] = _scalar(value)
                continue
            raise CheckpointError(f"{location}:{lineno}: invalid validation entry")

        raise CheckpointError(
            f"{location}:{lineno}: scalar key {current_key!r} cannot have nested values"
        )

    return data


def parse_task_checkpoint(path: Path) -> ParsedCheckpoint | None:
    text = path.read_text(encoding="utf-8")
    block = extract_checkpoint_block(text, source=path)
    if block is None:
        return None
    return ParsedCheckpoint(
        data=parse_checkpoint_block(block, source=path),
        block=block,
        source=path,
    )


def _normalized_fact(value: str) -> str:
    return " ".join(value.casefold().split())


def validate_checkpoint(
    data: dict[str, object],
    contract: GovernanceContract,
    *,
    source: Path | None = None,
) -> list[str]:
    location = str(source) if source else "<checkpoint>"
    errors: list[str] = []

    missing = sorted(contract.required_fields - set(data))
    for key in missing:
        errors.append(f"{location}: missing checkpoint field {key}")

    if str(data.get("checkpoint_version", "")).strip() != contract.checkpoint_version:
        errors.append(
            f"{location}: checkpoint_version must be {contract.checkpoint_version} "
            "as declared by docs/agents/GOVERNANCE_CONTRACT.json"
        )

    status = str(data.get("status", "")).strip()
    if status and status not in contract.allowed_statuses:
        errors.append(
            f"{location}: unsupported checkpoint status {status!r}; expected one of "
            f"{', '.join(sorted(contract.allowed_statuses))}"
        )

    for key in ("updated_at", "head", "branch", "pr", "next_action"):
        if key in data and not str(data.get(key, "")).strip():
            errors.append(f"{location}: checkpoint field {key} must not be empty")

    next_action = str(data.get("next_action", "")).strip()
    if next_action.casefold() in PLACEHOLDER_NEXT_ACTIONS:
        errors.append(f"{location}: next_action must be one concrete next step")

    first_failure = data.get("first_failure")
    if isinstance(first_failure, dict):
        for key in ("marker", "evidence"):
            if not str(first_failure.get(key, "")).strip():
                errors.append(f"{location}: first_failure.{key} must not be empty")
    elif "first_failure" in data:
        errors.append(f"{location}: first_failure must be a mapping")

    validation = data.get("validation")
    if isinstance(validation, list):
        for index, item in enumerate(validation, start=1):
            if not isinstance(item, dict):
                errors.append(f"{location}: validation item {index} must be a mapping")
                continue
            for key in ("command", "result", "evidence"):
                if not str(item.get(key, "")).strip():
                    errors.append(f"{location}: validation item {index} missing {key}")
            result = str(item.get("result", "")).strip()
            if result and result not in contract.allowed_validation_results:
                errors.append(
                    f"{location}: validation item {index} has unsupported result {result!r}; "
                    f"expected one of {', '.join(sorted(contract.allowed_validation_results))}"
                )
    elif "validation" in data:
        errors.append(f"{location}: validation must be a list")

    evidence_lists: dict[str, set[str]] = {}
    for key in contract.evidence_fields:
        raw = data.get(key, [])
        if not isinstance(raw, list):
            errors.append(f"{location}: {key} must be a list")
            continue
        values = {_normalized_fact(str(item)) for item in raw if str(item).strip()}
        evidence_lists[key] = values

    keys = list(evidence_lists)
    for index, left in enumerate(keys):
        for right in keys[index + 1 :]:
            overlap = evidence_lists[left] & evidence_lists[right]
            for fact in sorted(overlap):
                errors.append(
                    f"{location}: evidence fact appears in both {left} and {right}: {fact!r}"
                )

    return errors


def validate_task(
    path: Path,
    contract: GovernanceContract,
    *,
    require_checkpoint: bool = False,
) -> list[str]:
    try:
        parsed = parse_task_checkpoint(path)
    except (OSError, CheckpointError) as exc:
        return [str(exc)]

    if parsed is None:
        if require_checkpoint:
            return [f"{path}: missing {CHECKPOINT_HEADING} section"]
        return []
    return validate_checkpoint(parsed.data, contract, source=path)


def _task_files(root: Path) -> Iterable[Path]:
    return sorted(path for path in root.rglob("*.md") if path.name.casefold() != "readme.md")


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(
        description="Validate compact autonomous-agent context checkpoints."
    )
    parser.add_argument("paths", nargs="*", type=Path)
    parser.add_argument("--tasks", type=Path)
    parser.add_argument("--contract", type=Path, default=None)
    parser.add_argument(
        "--require-checkpoint",
        action="store_true",
        help="Fail when a selected task has no Context checkpoint section.",
    )
    args = parser.parse_args(argv)

    try:
        contract = load_contract(args.contract)
    except CheckpointError as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 1

    paths = list(args.paths)
    if args.tasks:
        paths.extend(_task_files(args.tasks))
    if not paths:
        parser.error("provide at least one task path or --tasks directory")

    errors: list[str] = []
    for path in paths:
        errors.extend(
            validate_task(path, contract, require_checkpoint=args.require_checkpoint)
        )

    if errors:
        for error in errors:
            print(f"ERROR: {error}", file=sys.stderr)
        return 1

    print(f"Validated {len(paths)} task checkpoint(s) against contract v{contract.checkpoint_version}.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

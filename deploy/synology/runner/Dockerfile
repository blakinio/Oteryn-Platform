FROM docker:cli AS docker-cli

FROM ghcr.io/actions/actions-runner:latest

USER root

COPY --from=docker-cli /usr/local/bin/docker /usr/local/bin/docker
COPY --from=docker-cli /usr/local/libexec/docker/cli-plugins/docker-compose /usr/local/libexec/docker/cli-plugins/docker-compose

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates coreutils curl \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /opt/actions-runner-dist /runner /work \
    && cp -a /home/runner/. /opt/actions-runner-dist/ \
    && chown -R runner:runner /opt/actions-runner-dist /runner /work

COPY --chown=runner:runner deploy/synology/runner/entrypoint.sh /usr/local/bin/oteryn-runner-entrypoint

USER runner
WORKDIR /runner

ENTRYPOINT ["/usr/local/bin/oteryn-runner-entrypoint"]

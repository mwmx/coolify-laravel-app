#!/bin/bash

set -eu

DOCKER_BUILDKIT=1 docker build . -t "$1"

echo now you can say docker push "$1"

#!/bin/bash

set -eu

DOCKER_BUILDKIT=1 docker build . -t "$1"

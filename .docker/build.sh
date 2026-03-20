#!/bin/bash
# docker login -u
docker build \
  -t equalframework/equal:latest \
  -t equalframework/equal:dev-2.0 \
  .
# docker push equalframework/equal:latest
# docker push equalframework/equal:dev-2.0

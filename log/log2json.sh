#!/bin/bash
# converts all lines from current equal.log file to a valid JSON object (array) and send to STDOUT
echo "["; awk '{print $0","}' equal.log | sed '$ s/,$//'; echo "]"

# usage

# ./log2json.sh

# This script can be used in conjunction with a JSON parser like jqlang (https://jqlang.github.io/)

# examples

# unique threads identifiers
# ./log2json.sh | ./jq.exe '[.[].thread_id] | unique'

# datetimes of all requests (1 per thread)
# ./log2json.sh | ./jq.exe '.[] | select(.mode == "NET" and .level == "SYSTEM") | [.time]'

# simplified version with time and message of all items from a given thread
# ./log2json.sh | ./jq.exe '.[] | select(.thread_id == "e208e52f") | {time: .time, message: .message}'
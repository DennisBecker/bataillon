#!/usr/bin/env bash

now=$(date +"%m_%d_%Y")

git add --all --force .
git commit --message "Updates guild data - $now"
git push --quiet "https://${GITHUB_TOKEN}@github.com/DennisBecker/bataillon.git" HEAD:master
#!/usr/bin/env bash

now=$(date +"%m_%d_%Y")

git add --force .
git commit --message "Updates guild data - $now"
git push --quit "https://${GITHUB_TOKEN}@github.com/DennisBecker/bataillon.git" origin master
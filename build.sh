#!/bin/bash

go mod download
cd cmd/openapi-mock
go build -v

package application

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

const (
	testSpecificationURL = "./../../test/resources/openapi-files/ValueGeneration.yaml"
	testConfigFilename   = "./../../test/resources/config.yaml"
)

func TestExecute_ValidArguments_NoError(t *testing.T) {
	tests := []struct {
		name      string
		arguments []string
	}{
		{
			"serve command with short url",
			[]string{
				"serve",
				"-u",
				testSpecificationURL,
			},
		},
		{
			"serve command with long url",
			[]string{
				"serve",
				"--specification-url",
				testSpecificationURL,
			},
		},
		{
			"serve command with url from config (short)",
			[]string{
				"serve",
				"-c",
				testConfigFilename,
			},
		},
		{
			"serve command with url from config (long)",
			[]string{
				"serve",
				"--configuration",
				testConfigFilename,
			},
		},
		{
			"validate command with short url",
			[]string{
				"validate",
				"-u",
				testSpecificationURL,
			},
		},
		{
			"validate command with long url",
			[]string{
				"validate",
				"--specification-url",
				testSpecificationURL,
			},
		},
		{
			"help command with short argument",
			[]string{"-h"},
		},
		{
			"help command with long argument",
			[]string{"--help"},
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			args := append(test.arguments, "--dry-run")

			err := Execute(Arguments(args))

			assert.NoError(t, err)
		})
	}
}

func TestExecute_InvalidArguments_Error(t *testing.T) {
	tests := []struct {
		name      string
		arguments []string
		error     string
	}{
		{
			"no specification url",
			[]string{"serve"},
			"failed to load OpenAPI specification from '': open : no such file or directory",
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			args := append(test.arguments, "--dry-run")

			err := Execute(Arguments(args))

			assert.EqualError(t, err, test.error)
		})
	}
}

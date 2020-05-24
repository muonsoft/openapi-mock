package console

import (
	"github.com/stretchr/testify/assert"
	"swagger-mock/internal/application/console/command/check"
	"swagger-mock/internal/application/console/command/run"
	"testing"
)

const (
	testSpecificationURL = "./../../../test/resources/openapi-files/ValueGeneration.yaml"
	testConfigFilename   = "./../../../test/resources/config.yaml"
)

func TestCreateCommand_ValidArguments_ExpectedCommandCreated(t *testing.T) {
	tests := []struct {
		name            string
		arguments       []string
		expectedCommand Command
	}{
		{
			"run command with short url",
			[]string{
				"run",
				"-u",
				testSpecificationURL,
			},
			&run.Command{},
		},
		{
			"run command with long url",
			[]string{
				"run",
				"--url",
				testSpecificationURL,
			},
			&run.Command{},
		},
		{
			"run command with url from config (short)",
			[]string{
				"run",
				"-c",
				testConfigFilename,
			},
			&run.Command{},
		},
		{
			"run command with url from config (long)",
			[]string{
				"run",
				"--configuration",
				testConfigFilename,
			},
			&run.Command{},
		},
		{
			"check command with short url",
			[]string{
				"check",
				"-u",
				testSpecificationURL,
			},
			&check.Command{},
		},
		{
			"check command with long url",
			[]string{
				"check",
				"--url",
				testSpecificationURL,
			},
			&check.Command{},
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			command, err := CreateCommand(test.arguments)

			assert.NoError(t, err)
			assert.IsType(t, test.expectedCommand, command)
		})
	}
}

func TestCreateCommand_InvalidArguments_Error(t *testing.T) {
	tests := []struct {
		name      string
		arguments []string
		exitCode  int
	}{
		{
			"empty arguments",
			[]string{},
			1,
		},
		{
			"help command with short argument",
			[]string{"-h"},
			0,
		},
		{
			"help command with long argument",
			[]string{"--help"},
			0,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			command, err := CreateCommand(test.arguments)

			assert.Nil(t, command)
			assert.Equal(t, test.exitCode, err.(*Error).ExitCode)
		})
	}
}

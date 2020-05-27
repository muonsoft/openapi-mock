package console

import (
	"github.com/muonsoft/openapi-mock/internal/application/console/command/serve"
	"github.com/muonsoft/openapi-mock/internal/application/console/command/validate"
	"github.com/stretchr/testify/assert"
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
			"serve command with short url",
			[]string{
				"serve",
				"-u",
				testSpecificationURL,
			},
			&serve.Command{},
		},
		{
			"serve command with long url",
			[]string{
				"serve",
				"--specification-url",
				testSpecificationURL,
			},
			&serve.Command{},
		},
		{
			"serve command with url from config (short)",
			[]string{
				"serve",
				"-c",
				testConfigFilename,
			},
			&serve.Command{},
		},
		{
			"serve command with url from config (long)",
			[]string{
				"serve",
				"--configuration",
				testConfigFilename,
			},
			&serve.Command{},
		},
		{
			"validate command with short url",
			[]string{
				"validate",
				"-u",
				testSpecificationURL,
			},
			&validate.Command{},
		},
		{
			"validate command with long url",
			[]string{
				"validate",
				"--specification-url",
				testSpecificationURL,
			},
			&validate.Command{},
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

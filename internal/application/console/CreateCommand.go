package console

import (
	"github.com/jessevdk/go-flags"
	"os"
	"swagger-mock/internal/application/config"
	"swagger-mock/internal/application/console/command/run"
	"swagger-mock/internal/application/console/command/validate"
)

func CreateCommand() Command {
	options := &Options{}
	parser := flags.NewParser(options, flags.Default)
	_, _ = parser.AddCommand("run", "Runs an OpenAPI mock server", "", options)
	_, _ = parser.AddCommand("validate", "Validates an OpenAPI specification", "", options)

	_, err := parser.Parse()
	if err != nil {
		os.Exit(0)
	}

	configuration := config.LoadFromEnvironment()

	if options.URL != "" {
		configuration.SpecificationURL = options.URL
	}

	var command Command

	switch parser.Active.Name {
	case "run":
		command = run.NewCommand(configuration)
	case "validate":
		command = validate.NewCommand(configuration)
	}

	return command
}

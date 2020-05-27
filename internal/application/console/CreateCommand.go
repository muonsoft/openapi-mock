package console

import (
	"fmt"
	"github.com/jessevdk/go-flags"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	"github.com/muonsoft/openapi-mock/internal/application/console/command/serve"
	"github.com/muonsoft/openapi-mock/internal/application/console/command/validate"
	"github.com/muonsoft/openapi-mock/internal/application/container"
	"github.com/pkg/errors"
)

func CreateCommand(arguments []string) (Command, error) {
	options, err := parseCommandLine(arguments)
	if err != nil {
		return nil, err
	}

	configuration, err := config.Load(options.ConfigFilename)
	if err != nil {
		fmt.Printf("%v", err)
		return nil, &Error{Previous: err, ExitCode: 1}
	}

	if options.URL != "" {
		configuration.SpecificationURL = options.URL
	}

	return createConsoleCommand(options.CommandName, configuration), nil
}

func parseCommandLine(arguments []string) (*Options, error) {
	options := &Options{}
	parser := flags.NewParser(options, flags.Default)
	_, _ = parser.AddGroup("Show version", "", &VersionArguments{})
	_, _ = parser.AddCommand(
		"serve",
		"Starts an HTTP server for generating mock responses by OpenAPI specification",
		"",
		options,
	)
	_, _ = parser.AddCommand(
		"validate",
		"Validates an OpenAPI specification",
		"",
		options,
	)

	_, err := parser.ParseArgs(arguments)
	if err != nil {
		exitCode := 1
		var flagError *flags.Error
		if errors.As(err, &flagError) && flagError.Type == flags.ErrHelp {
			exitCode = 0
		}
		return nil, &Error{
			ExitCode: exitCode,
			Previous: err,
		}
	}

	options.CommandName = parser.Active.Name

	return options, nil
}

func createConsoleCommand(commandName string, configuration *config.Configuration) Command {
	appContainer := container.New(configuration)

	var command Command

	switch commandName {
	case "serve":
		httpServer := appContainer.CreateHTTPServer()
		command = serve.NewCommand(httpServer)
	case "validate":
		specificationLoader := appContainer.CreateSpecificationLoader()
		command = validate.NewCommand(configuration.SpecificationURL, specificationLoader)
	}

	return command
}

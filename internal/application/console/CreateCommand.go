package console

import (
	"fmt"
	"github.com/jessevdk/go-flags"
	"github.com/pkg/errors"
	"swagger-mock/internal/application/config"
	"swagger-mock/internal/application/console/command/check"
	"swagger-mock/internal/application/console/command/run"
	"swagger-mock/internal/application/container"
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
	_, _ = parser.AddCommand("run", "Runs an OpenAPI mock server", "", options)
	_, _ = parser.AddCommand("check", "Checks that an OpenAPI specification can be loaded", "", options)

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
	case "run":
		httpServer := appContainer.CreateHTTPServer()
		command = run.NewCommand(httpServer)
	case "check":
		specificationLoader := appContainer.CreateSpecificationLoader()
		command = check.NewCommand(configuration.SpecificationURL, specificationLoader)
	}

	return command
}

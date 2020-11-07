package application

import (
	"context"
	"fmt"
	"log"

	"github.com/muonsoft/openapi-mock/internal/application/config"
	"github.com/muonsoft/openapi-mock/internal/application/di"
	"github.com/spf13/cobra"
)

const descriptionTemplate = `OpenAPI Mock tool with random data generation.
Version %s. Build at %s.

See documentation at https://github.com/muonsoft/openapi-mock/blob/master/docs/usage_guide.md.`

type Options struct {
	SpecificationURL  string
	Version           string
	BuildTime         string
	ConfigFilename    string
	DryRun            bool
	Arguments         []string
	overrideArguments bool
}

type OptionFunc func(options *Options)

func Version(version string) OptionFunc {
	return func(options *Options) {
		options.Version = version
	}
}

func BuildTime(buildTime string) OptionFunc {
	return func(options *Options) {
		options.BuildTime = buildTime
	}
}

func Arguments(args []string) OptionFunc {
	return func(options *Options) {
		options.Arguments = args
		options.overrideArguments = true
	}
}

func Execute(options ...OptionFunc) error {
	opts := &Options{}
	for _, setOption := range options {
		setOption(opts)
	}

	mainCommand := newMainCommand(opts)
	if opts.overrideArguments {
		mainCommand.SetArgs(opts.Arguments)
	}

	return mainCommand.Execute()
}

func newMainCommand(opts *Options) *cobra.Command {
	mainCommand := &cobra.Command{
		Use:   "openapi-mock",
		Short: "OpenAPI Mock tool with random data generation",
		Long:  fmt.Sprintf(descriptionTemplate, opts.Version, opts.BuildTime),
	}

	mainCommand.PersistentFlags().StringVarP(
		&opts.ConfigFilename,
		"configuration",
		"c",
		"",
		`Configuration filename in JSON/YAML format. By default configuration is loaded from 'openapi-mock.yaml', 'openapi-mock.yml' or 'openapi-mock.json'.`,
	)
	mainCommand.PersistentFlags().StringVarP(
		&opts.SpecificationURL,
		"specification-url",
		"u",
		"",
		`URL or path to file with OpenAPI v3 specification. Overrides specification defined in configuration file or environment variable.`,
	)
	mainCommand.PersistentFlags().BoolVar(&opts.DryRun, "dry-run", false, `Dry run will not start a server`)

	mainCommand.AddCommand(
		newVersionCommand(opts),
		newServeCommand(opts),
		newValidateCommand(opts),
	)

	return mainCommand
}

func newVersionCommand(options *Options) *cobra.Command {
	return &cobra.Command{
		Use:   "version",
		Short: "Prints application version",
		Run: func(cmd *cobra.Command, args []string) {
			fmt.Printf("OpenAPI Mock tool. Version %s built at %s.\n", options.Version, options.BuildTime)
		},
	}
}

func newServeCommand(options *Options) *cobra.Command {
	return &cobra.Command{
		Use:           "serve",
		Short:         "Starts an HTTP server for generating mock responses by OpenAPI specification",
		SilenceUsage:  true,
		SilenceErrors: true,
		RunE: func(cmd *cobra.Command, args []string) error {
			configuration, err := config.Load(options.ConfigFilename)
			if err != nil {
				return err
			}
			configuration.DryRun = options.DryRun
			if options.SpecificationURL != "" {
				configuration.SpecificationURL = options.SpecificationURL
			}

			factory := di.NewFactory(configuration)
			server, err := factory.CreateHTTPServer()
			if err != nil {
				return err
			}

			if !options.DryRun {
				err = server.Run()
				if err != nil {
					return err
				}
			}

			return nil
		},
	}
}

func newValidateCommand(options *Options) *cobra.Command {
	return &cobra.Command{
		Use:           "validate",
		Short:         "Validates an OpenAPI specification",
		SilenceUsage:  true,
		SilenceErrors: true,
		RunE: func(cmd *cobra.Command, args []string) error {
			configuration, err := config.Load(options.ConfigFilename)
			if err != nil {
				return err
			}
			if options.SpecificationURL != "" {
				configuration.SpecificationURL = options.SpecificationURL
			}

			factory := di.NewFactory(configuration)
			loader := factory.CreateSpecificationLoader()
			specification, err := loader.LoadFromURI(configuration.SpecificationURL)
			if err != nil {
				return err
			}

			err = specification.Validate(context.Background())
			if err != nil {
				return fmt.Errorf(
					"validation of OpenAPI specification '%s' failed: %w",
					configuration.SpecificationURL,
					err,
				)
			}

			if !options.DryRun {
				log.Printf("OpenAPI specification '%s' is valid", configuration.SpecificationURL)
			}

			return nil
		},
	}
}

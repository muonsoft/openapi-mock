package console

type Options struct {
	ConfigFilename string `short:"c" long:"configuration" default:"openapi-mock.yaml" description:"Destination to configuration filename in JSON/YAML format"`
	URL            string `short:"u" long:"url" description:"URL or path to file with OpenAPI v3 specification"`
}

package console

type Options struct {
	CommandName    string `no-flag:"true"`
	ConfigFilename string `short:"c" long:"configuration" description:"Destination to configuration filename in JSON/YAML format"`
	URL            string `short:"u" long:"url" description:"URL or path to file with OpenAPI v3 specification"`
}

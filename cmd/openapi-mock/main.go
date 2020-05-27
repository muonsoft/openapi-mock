package main

import (
	"fmt"
	"github.com/muonsoft/openapi-mock/internal/application/console"
	"log"
	"os"
)

var (
	version   string
	buildTime string
)

func main() {
	arguments := os.Args[1:]
	if len(arguments) > 0 && (arguments[0] == "-v" || arguments[0] == "--version") {
		fmt.Printf("OpenAPI Mock server version %s built at %s.\n", version, buildTime)
		os.Exit(0)
	}

	command, err := console.CreateCommand(arguments)
	if err != nil {
		os.Exit(err.(*console.Error).ExitCode)
	}

	err = command.Execute()
	if err != nil {
		log.Fatal(err)
	}
}

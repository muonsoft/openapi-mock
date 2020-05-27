package main

import (
	"fmt"
	"log"
	"os"
	"swagger-mock/internal/application/console"
)

var version string

func main() {
	arguments := os.Args[1:]
	if len(arguments) > 0 && (arguments[0] == "-v" || arguments[0] == "--version") {
		fmt.Printf("OpenAPI Mock server version %s.\n", version)
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

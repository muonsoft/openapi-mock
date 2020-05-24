package main

import (
	"log"
	"os"
	"swagger-mock/internal/application/console"
)

func main() {
	command, err := console.CreateCommand(os.Args[1:])
	if err != nil {
		os.Exit(err.(*console.Error).ExitCode)
	}

	err = command.Execute()
	if err != nil {
		log.Fatal(err)
	}
}

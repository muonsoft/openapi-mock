package main

import (
	"log"
	"swagger-mock/internal/console"
)

func main() {
	command := console.CreateCommand()
	err := command.Execute()
	if err != nil {
		log.Fatal(err)
	}
}

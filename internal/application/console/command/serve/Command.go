package serve

import (
	"github.com/muonsoft/openapi-mock/internal/server"
)

type Command struct {
	server server.Server
}

func NewCommand(httpServer server.Server) *Command {
	return &Command{server: httpServer}
}

func (command *Command) Execute() error {
	return command.server.Run()
}

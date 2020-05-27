package console

import "fmt"

type Error struct {
	ExitCode int
	Previous error
}

func (err *Error) Error() string {
	return fmt.Sprintf("command error: %s", err.Previous.Error())
}

func (err *Error) Unwrap() error {
	return err.Previous
}

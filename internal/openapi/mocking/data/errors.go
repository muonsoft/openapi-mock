package data

import (
	"errors"
	"fmt"
)

type ErrGenerationFailed struct {
	GeneratorID string
	Message     string
	Path        string
	Previous    error
}

func (err *ErrGenerationFailed) Error() string {
	if err.Previous == nil {
		return fmt.Sprintf("[%s] %s", err.GeneratorID, err.Message)
	}

	return fmt.Sprintf("[%s] %s: %s", err.GeneratorID, err.Message, err.Previous.Error())
}

func (err *ErrGenerationFailed) Unwrap() error {
	return err.Previous
}

var errAttemptsLimitExceeded = errors.New("attempts limit exceeded")

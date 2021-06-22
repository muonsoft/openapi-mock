package config

import (
	"fmt"
	"strings"

	"github.com/muonsoft/validation"
)

type LoadingFailedError struct {
	Previous error
}

func (err *LoadingFailedError) Error() string {
	return fmt.Sprintf("failed to load configuration: %s", err.Previous.Error())
}

func (err *LoadingFailedError) Unwrap() error {
	return err.Previous
}

type InvalidConfigurationError struct {
	ValidationError error
}

func (err *InvalidConfigurationError) Error() string {
	violations, ok := validation.UnwrapViolationList(err.ValidationError)
	if !ok {
		return fmt.Sprintf("failed to validate configuration: %s", err.ValidationError)
	}

	var message strings.Builder
	message.WriteString("configuration has invalid values: ")

	for violation := violations.First(); violation != nil; violation = violation.Next() {
		if violation != violations.First() {
			message.WriteString("; ")
		}
		message.WriteString(fmt.Sprintf("invalid option '%s': %s", violation.PropertyPath().String(), violation.Message()))
	}

	return message.String()
}

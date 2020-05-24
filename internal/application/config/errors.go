package config

import (
	"fmt"
	"github.com/asaskevich/govalidator"
	"strings"
)

type ErrLoadFailed struct {
	Previous error
}

func (err *ErrLoadFailed) Error() string {
	return fmt.Sprintf("failed to load configuration: %s", err.Previous.Error())
}

func (err *ErrLoadFailed) Unwrap() error {
	return err.Previous
}

type ErrInvalidConfiguration struct {
	ValidationError error
}

func (err *ErrInvalidConfiguration) Error() string {
	violations := err.ValidationError.(govalidator.Errors).Errors()
	formattedViolations := make([]string, 0)
	for i := range violations {
		attributeViolations := violations[i].(govalidator.Errors)
		for j := range attributeViolations {
			violation := attributeViolations[j].(govalidator.Error)
			formattedViolations = append(formattedViolations, fmt.Sprintf("invalid option '%s': %s", violation.Name, violation.Err.Error()))
		}
	}

	return fmt.Sprintf("configuration has invalid values: %s", strings.Join(formattedViolations, "; "))
}

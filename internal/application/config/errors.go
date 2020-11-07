package config

import (
	"fmt"
	"sort"
	"strings"

	validation "github.com/go-ozzo/ozzo-validation/v4"
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
	violations := err.ValidationError.(validation.Errors)

	keys := make([]string, 0, len(violations))
	for key := range violations {
		keys = append(keys, key)
	}
	sort.Strings(keys)

	var message strings.Builder
	message.WriteString("configuration has invalid values: ")

	for i, key := range keys {
		err := violations[key]
		if err != nil {
			if i > 0 {
				message.WriteString("; ")
			}
			message.WriteString(fmt.Sprintf("invalid option '%s': %s", key, err.Error()))
		}
	}

	return message.String()
}

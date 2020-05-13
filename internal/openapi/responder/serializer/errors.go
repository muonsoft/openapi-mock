package serializer

import (
	"errors"
	"fmt"
)

var ErrUnserializableData = errors.New("unserializable data")

type UnsupportedFormatError struct {
	Format string
}

func (err *UnsupportedFormatError) Error() string {
	return fmt.Sprintf("serialization format '%s' is not supported", err.Format)
}

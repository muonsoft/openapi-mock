package errors

type NotSupportedError struct {
	Message string
}

func NotSupported(message string) error {
	return NotSupportedError{Message: message}
}

func (err NotSupportedError) Error() string {
	return err.Message
}

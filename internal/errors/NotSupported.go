package errors

type NotSupported struct {
	Message string
}

func NewNotSupported(message string) *NotSupported {
	return &NotSupported{Message: message}
}

func (err *NotSupported) Error() string {
	return err.Message
}

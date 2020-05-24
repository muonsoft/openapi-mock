package console

type Command interface {
	Execute() error
}

package generator

type Options struct {
	UseExamples UseExamplesEnum
}

type UseExamplesEnum int

const (
	No UseExamplesEnum = iota
	IfPresent
	Exclusively
)

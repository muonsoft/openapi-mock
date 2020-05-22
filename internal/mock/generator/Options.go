package generator

type Options struct {
	UseExamples     UseExamplesEnum
	NullProbability float64
	SuppressErrors  bool
}

type UseExamplesEnum int

const (
	No UseExamplesEnum = iota
	IfPresent
	Exclusively
)

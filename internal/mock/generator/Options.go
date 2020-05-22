package generator

type Options struct {
	UseExamples     UseExamplesEnum
	NullProbability float64
	DefaultMinInt   int64
	DefaultMaxInt   int64
	DefaultMinFloat float64
	DefaultMaxFloat float64
	SuppressErrors  bool
}

type UseExamplesEnum int

const (
	No UseExamplesEnum = iota
	IfPresent
	Exclusively
)

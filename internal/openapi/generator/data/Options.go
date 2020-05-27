package data

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

func (enum UseExamplesEnum) String() string {
	switch enum {
	case No:
		return "no"
	case IfPresent:
		return "if_present"
	case Exclusively:
		return "exclusively"
	}

	return "unknown"
}

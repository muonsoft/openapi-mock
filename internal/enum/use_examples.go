package enum

type UseExamples int

const (
	No UseExamples = iota
	IfPresent
	Exclusively
)

func (enum UseExamples) String() string {
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

package data

type arrayLengthGenerator interface {
	GenerateLength(min uint64, max uint64) (length uint64, minLength uint64)
}

type randomArrayLengthGenerator struct {
	random randomGenerator
}

func (generator *randomArrayLengthGenerator) GenerateLength(min uint64, max uint64) (length uint64, minLength uint64) {
	minItems := min
	maxItems := uint64(defaultMaxItems)
	if max > 0 {
		maxItems = max
	}

	if maxItems <= minItems {
		return minItems, minItems
	}

	length = uint64(generator.random.Intn(int(maxItems-minItems+1))) + minItems

	return length, minItems
}

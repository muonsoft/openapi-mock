package generator

// Contract for random generator compatible with rand.Rand from math/rand package.
type randomGenerator interface {
	Float64() float64
	Intn(n int) int
}

package data

import "github.com/muonsoft/openapi-mock/internal/enum"

type Options struct {
	UseExamples     enum.UseExamples
	NullProbability float64
	DefaultMinInt   int64
	DefaultMaxInt   int64
	DefaultMinFloat float64
	DefaultMaxFloat float64
	SuppressErrors  bool
}

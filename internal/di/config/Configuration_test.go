package config

import (
	"github.com/sirupsen/logrus"
	"github.com/stretchr/testify/assert"
	"os"
	"swagger-mock/internal/mock/generator"
	"testing"
)

func TestLoadFromEnvironment_AllParametersInEnv_ParametersLoaded(t *testing.T) {
	resetEnvironment()
	_ = os.Setenv("SWAGGER_MOCK_SPECIFICATION_URL", "specification_url")
	_ = os.Setenv("SWAGGER_MOCK_USE_EXAMPLES", "if_present")
	_ = os.Setenv("SWAGGER_MOCK_NULL_PROBABILITY", "0.8")
	_ = os.Setenv("SWAGGER_MOCK_CORS_ENABLED", "1")
	_ = os.Setenv("SWAGGER_MOCK_PORT", "1234")
	_ = os.Setenv("SWAGGER_MOCK_LOG_LEVEL", "error")
	_ = os.Setenv("SWAGGER_MOCK_LOG_FORMAT", "json")
	_ = os.Setenv("SWAGGER_MOCK_DEBUG", "0")

	config := LoadFromEnvironment()

	assert.Equal(t, "specification_url", config.SpecificationURL)
	assert.Equal(t, generator.IfPresent, config.UseExamples)
	assert.Equal(t, 0.8, config.NullProbability)
	assert.True(t, config.CORSEnabled)
	assert.False(t, config.Debug)
	assert.Equal(t, logrus.ErrorLevel, config.LogLevel)
	assert.Equal(t, "json", config.LogFormat)
}

func TestLoadFromEnvironment_OnlyRequiredParametersInEnv_DefaultParametersLoaded(t *testing.T) {
	resetEnvironment()
	_ = os.Setenv("SWAGGER_MOCK_SPECIFICATION_URL", "specification_url")

	config := LoadFromEnvironment()

	assert.Equal(t, "specification_url", config.SpecificationURL)
	assert.Equal(t, generator.No, config.UseExamples)
	assert.Equal(t, 0.5, config.NullProbability)
	assert.False(t, config.CORSEnabled)
	assert.False(t, config.Debug)
	assert.Equal(t, logrus.WarnLevel, config.LogLevel)
	assert.Equal(t, "tty", config.LogFormat)
}

func TestLoadFromEnvironment_DebugIsOn_LogLevelIsTrace(t *testing.T) {
	resetEnvironment()
	_ = os.Setenv("SWAGGER_MOCK_SPECIFICATION_URL", "specification_url")
	_ = os.Setenv("SWAGGER_MOCK_DEBUG", "1")

	config := LoadFromEnvironment()

	assert.True(t, config.Debug)
	assert.Equal(t, logrus.TraceLevel, config.LogLevel)
}

func TestLoadFromEnvironment_UseExamplesOption_ExpectedValue(t *testing.T) {
	tests := []struct {
		useExamples         string
		expectedUseExamples generator.UseExamplesEnum
	}{
		{
			"no",
			generator.No,
		},
		{
			"",
			generator.No,
		},
		{
			"if_present",
			generator.IfPresent,
		},
		{
			"exclusively",
			generator.Exclusively,
		},
	}
	for _, test := range tests {
		t.Run(test.useExamples, func(t *testing.T) {
			resetEnvironment()
			_ = os.Setenv("SWAGGER_MOCK_SPECIFICATION_URL", "specification_url")
			_ = os.Setenv("SWAGGER_MOCK_USE_EXAMPLES", test.useExamples)

			config := LoadFromEnvironment()

			assert.Equal(t, test.expectedUseExamples, config.UseExamples)
		})
	}
}

func resetEnvironment() {
	_ = os.Unsetenv("SWAGGER_MOCK_SPECIFICATION_URL")
	_ = os.Unsetenv("SWAGGER_MOCK_USE_EXAMPLES")
	_ = os.Unsetenv("SWAGGER_MOCK_NULL_PROBABILITY")
	_ = os.Unsetenv("SWAGGER_MOCK_CORS_ENABLED")
	_ = os.Unsetenv("SWAGGER_MOCK_PORT")
	_ = os.Unsetenv("SWAGGER_MOCK_LOG_LEVEL")
	_ = os.Unsetenv("SWAGGER_MOCK_LOG_FORMAT")
	_ = os.Unsetenv("SWAGGER_MOCK_DEBUG")
}

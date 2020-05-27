package config

import (
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/data"
	"github.com/sirupsen/logrus"
	"github.com/stretchr/testify/assert"
	"math"
	"os"
	"testing"
	"time"
)

const (
	testResourcesDir    = "../../../test/resources/"
	testCustomConfigDir = testResourcesDir + "./openapi-files/ValueGeneration.yaml"
)

func TestLoad_YAMLFile_ConfigurationWithExpectedValues(t *testing.T) {
	resetEnvironment()

	config, err := Load(testResourcesDir + "config.yaml")

	assert.NoError(t, err)
	assertIsModifiedConfiguration(t, config)
}

func TestLoad_JSONFile_ConfigurationWithExpectedValues(t *testing.T) {
	resetEnvironment()

	config, err := Load(testResourcesDir + "config.json")

	assert.NoError(t, err)
	assertIsModifiedConfiguration(t, config)
}

func TestLoad_EmptyConfigFile_ConfigurationWithDefaultValues(t *testing.T) {
	resetEnvironment()

	config, err := Load(testResourcesDir + "empty-config.yaml")

	assert.NoError(t, err)
	assertIsDefaultConfiguration(t, config)
}

func TestLoad_EmptyFilenameAndNoEnvParams_ConfigurationWithDefaultValues(t *testing.T) {
	resetEnvironment()

	config, err := Load("")

	assert.NoError(t, err)
	assertIsDefaultConfiguration(t, config)
}

func TestLoad_NoFileAndAllEnvironmentParams_ExpectedValues(t *testing.T) {
	resetEnvironment()
	givenAllEnvironmentParameters()

	config, err := Load("")

	assert.NoError(t, err)
	assertIsModifiedConfiguration(t, config)
}

func TestLoad_EmptyFileAndAllEnvironmentParams_ValuesFromEnvironment(t *testing.T) {
	resetEnvironment()
	givenAllEnvironmentParameters()

	config, err := Load("./../../../test/resources/empty-config.yaml")

	assert.NoError(t, err)
	assertIsModifiedConfiguration(t, config)
}

func TestLoad_NotExistingFilename_Error(t *testing.T) {
	resetEnvironment()

	config, err := Load("invalid")

	assert.Nil(t, config)
	assert.EqualError(t, err, "failed to load configuration: open invalid: no such file or directory")
}

func TestLoad_InvalidFile_Error(t *testing.T) {
	resetEnvironment()

	config, err := Load("errors.go")

	assert.Nil(t, config)
	assert.EqualError(t, err, "failed to load configuration: yaml: line 14: mapping values are not allowed in this context")
}

func TestLoad_FileWithInvalidValues_Error(t *testing.T) {
	resetEnvironment()

	config, err := Load("./../../../test/resources/invalid-config.yaml")

	assert.Nil(t, config)
	assert.EqualError(t, err, "configuration has invalid values: "+
		"invalid option 'port': 0 does not validate as range(1|65535); "+
		"invalid option 'log_format': invalid does not validate as in(tty|json); "+
		"invalid option 'log_level': invalid does not validate as in(panic|fatal|error|warn|warning|info|debug|trace); "+
		"invalid option 'use_examples': invalid does not validate as in(no|if_present|exclusively)")
}

func TestLoad_DebugIsOn_LogLevelIsTrace(t *testing.T) {
	resetEnvironment()
	_ = os.Setenv("OPENAPI_MOCK_DEBUG", "1")

	config, err := Load("")

	assert.NoError(t, err)
	assert.True(t, config.Debug)
	assert.Equal(t, logrus.TraceLevel, config.LogLevel)
}

func resetEnvironment() {
	_ = os.Unsetenv("OPENAPI_MOCK_SPECIFICATION_URL")
	_ = os.Unsetenv("OPENAPI_MOCK_USE_EXAMPLES")
	_ = os.Unsetenv("OPENAPI_MOCK_NULL_PROBABILITY")
	_ = os.Unsetenv("OPENAPI_MOCK_DEFAULT_MIN_INT")
	_ = os.Unsetenv("OPENAPI_MOCK_DEFAULT_MAX_INT")
	_ = os.Unsetenv("OPENAPI_MOCK_DEFAULT_MIN_FLOAT")
	_ = os.Unsetenv("OPENAPI_MOCK_DEFAULT_MAX_FLOAT")
	_ = os.Unsetenv("OPENAPI_MOCK_CORS_ENABLED")
	_ = os.Unsetenv("OPENAPI_MOCK_SUPPRESS_ERRORS")
	_ = os.Unsetenv("OPENAPI_MOCK_PORT")
	_ = os.Unsetenv("OPENAPI_MOCK_LOG_LEVEL")
	_ = os.Unsetenv("OPENAPI_MOCK_LOG_FORMAT")
	_ = os.Unsetenv("OPENAPI_MOCK_DEBUG")
}

func givenAllEnvironmentParameters() {
	_ = os.Setenv("OPENAPI_MOCK_SPECIFICATION_URL", testCustomConfigDir)
	_ = os.Setenv("OPENAPI_MOCK_CORS_ENABLED", "1")
	_ = os.Setenv("OPENAPI_MOCK_PORT", "8888")
	_ = os.Setenv("OPENAPI_MOCK_RESPONSE_TIMEOUT", "0.5")
	_ = os.Setenv("OPENAPI_MOCK_DEBUG", "0")
	_ = os.Setenv("OPENAPI_MOCK_LOG_FORMAT", "json")
	_ = os.Setenv("OPENAPI_MOCK_LOG_LEVEL", "error")
	_ = os.Setenv("OPENAPI_MOCK_DEFAULT_MIN_INT", "-123")
	_ = os.Setenv("OPENAPI_MOCK_DEFAULT_MAX_INT", "123")
	_ = os.Setenv("OPENAPI_MOCK_DEFAULT_MIN_FLOAT", "-123.123")
	_ = os.Setenv("OPENAPI_MOCK_DEFAULT_MAX_FLOAT", "123.123")
	_ = os.Setenv("OPENAPI_MOCK_NULL_PROBABILITY", "0.8")
	_ = os.Setenv("OPENAPI_MOCK_SUPPRESS_ERRORS", "1")
	_ = os.Setenv("OPENAPI_MOCK_USE_EXAMPLES", "if_present")
}

func assertIsModifiedConfiguration(t *testing.T, config *Configuration) {
	assert.Equal(t, testCustomConfigDir, config.SpecificationURL)

	assert.True(t, config.CORSEnabled)
	assert.Equal(t, uint16(8888), config.Port)
	assert.Equal(t, time.Second/2, config.ResponseTimeout)

	assert.False(t, config.Debug)
	assert.Equal(t, "json", config.LogFormat)
	assert.Equal(t, logrus.ErrorLevel, config.LogLevel)

	assert.Equal(t, -123.123, config.DefaultMinFloat)
	assert.Equal(t, 123.123, config.DefaultMaxFloat)
	assert.Equal(t, int64(-123), config.DefaultMinInt)
	assert.Equal(t, int64(123), config.DefaultMaxInt)
	assert.Equal(t, 0.8, config.NullProbability)
	assert.True(t, config.SuppressErrors)
	assert.Equal(t, data.IfPresent, config.UseExamples)
}

func assertIsDefaultConfiguration(t *testing.T, config *Configuration) {
	assert.Equal(t, "", config.SpecificationURL)

	assert.False(t, config.CORSEnabled)
	assert.Equal(t, uint16(8080), config.Port)
	assert.Equal(t, DefaultResponseTimeout, config.ResponseTimeout)

	assert.False(t, config.Debug)
	assert.Equal(t, logrus.InfoLevel, config.LogLevel)
	assert.Equal(t, "tty", config.LogFormat)

	assert.Equal(t, 0.5, config.NullProbability)
	assert.Equal(t, int64(0), config.DefaultMinInt)
	assert.Equal(t, int64(math.MaxInt32), config.DefaultMaxInt)
	assert.Equal(t, -float64(math.MaxInt32/2), config.DefaultMinFloat)
	assert.Equal(t, float64(math.MaxInt32/2), config.DefaultMaxFloat)
	assert.False(t, config.SuppressErrors)
	assert.Equal(t, data.No, config.UseExamples)
}

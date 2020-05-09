package jsonassert

import (
	"encoding/json"
	"fmt"
	"github.com/stretchr/testify/assert"
	"github.com/yalp/jsonpath"
	"testing"
)

type JSONAssert struct {
	t    *testing.T
	data interface{}
}

func Parse(t *testing.T, data []byte) (*JSONAssert, error) {
	body := &JSONAssert{
		t: t,
	}
	err := json.Unmarshal(data, &body.data)

	return body, err
}

func MustParse(t *testing.T, data []byte) *JSONAssert {
	body, err := Parse(t, data)
	if err != nil {
		panic(err)
	}

	return body
}

func (j *JSONAssert) AssertNodeDoesNotExist(path string, msgAndArgs ...interface{}) {
	_, err := jsonpath.Read(j.data, path)
	if err == nil {
		assert.Fail(j.t, fmt.Sprintf("failed asserting that json node '%s' does not exist", path), msgAndArgs)
	}
}

func (j *JSONAssert) AssertNodeShouldExist(path string, msgAndArgs ...interface{}) {
	_, err := jsonpath.Read(j.data, path)
	if err != nil {
		assert.Fail(j.t, fmt.Sprintf("failed asserting that json node '%s' should exist", path), msgAndArgs)
	}
}

func (j *JSONAssert) AssertNodeEqualToTheString(path string, expectedValue string, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.IsType(j.t, "string", value, msgAndArgs)
	assert.Equal(j.t, expectedValue, value, msgAndArgs)
}

func (j *JSONAssert) AssertNodeShouldMatch(path string, regexp string, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.IsType(j.t, "string", value, msgAndArgs)
	assert.Regexp(j.t, regexp, value, msgAndArgs)
}

func (j *JSONAssert) AssertNodeShouldContain(path string, contain string, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.IsType(j.t, "string", value, msgAndArgs)
	assert.Contains(j.t, value, contain, msgAndArgs)
}

func (j *JSONAssert) AssertArrayNodeShouldHaveElementsCount(path string, count int, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.Len(j.t, value, count, msgAndArgs)
}

func (j *JSONAssert) AssertNodeShouldBeAStringWithLengthInRange(path string, min int, max int, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.IsType(j.t, "string", value, msgAndArgs)
	assert.GreaterOrEqual(j.t, len(value.(string)), min, msgAndArgs)
	assert.LessOrEqual(j.t, len(value.(string)), max, msgAndArgs)
}

func (j *JSONAssert) AssertNodeShouldBeANumberInRange(path string, min float64, max float64, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.GreaterOrEqual(j.t, value, min, msgAndArgs)
	assert.LessOrEqual(j.t, value, max, msgAndArgs)
}

func (j *JSONAssert) AssertNodeEqualToTheInteger(path string, expectedValue int, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.IsType(j.t, 0, value, msgAndArgs)
	assert.Equal(j.t, expectedValue, value, msgAndArgs)
}

func (j *JSONAssert) AssertNodeEqualToTheFloat64(path string, expectedValue float64, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.IsType(j.t, 0.0, value, msgAndArgs)
	assert.Equal(j.t, expectedValue, value, msgAndArgs)
}

func (j *JSONAssert) AssertNodeIsTrue(path string, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.True(j.t, value.(bool), msgAndArgs)
}

func (j *JSONAssert) AssertNodeIsFalse(path string, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.False(j.t, value.(bool), msgAndArgs)
}

func (j *JSONAssert) AssertNodeIsNull(path string, msgAndArgs ...interface{}) {
	value := j.mustRead(path)
	assert.Nil(j.t, value, msgAndArgs)
}

func (j *JSONAssert) mustRead(path string) interface{} {
	value, err := jsonpath.Read(j.data, path)
	if err != nil {
		panic(fmt.Errorf("failed to read json path '%s': %s", path, err))
	}
	return value
}

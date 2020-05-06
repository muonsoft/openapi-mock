package jsonassert

import (
	"encoding/json"
	"fmt"
	"github.com/stretchr/testify/assert"
	"github.com/yalp/jsonpath"
	"testing"
)

type JsonAssert struct {
	t    *testing.T
	data interface{}
}

func Parse(t *testing.T, data []byte) (*JsonAssert, error) {
	body := &JsonAssert{
		t: t,
	}
	err := json.Unmarshal(data, &body.data)

	return body, err
}

func MustParse(t *testing.T, data []byte) *JsonAssert {
	body, err := Parse(t, data)
	if err != nil {
		panic(err)
	}

	return body
}

func (j *JsonAssert) AssertNodeEqualToTheString(path string, expectedValue string, msgAndArgs ...interface{}) {
	value := j.read(path)
	assert.IsType(j.t, "string", value, msgAndArgs)
	assert.Equal(j.t, expectedValue, value, msgAndArgs)
}

func (j *JsonAssert) AssertNodeEqualToTheInteger(path string, expectedValue int, msgAndArgs ...interface{}) {
	value := j.read(path)
	assert.IsType(j.t, 0, value, msgAndArgs)
	assert.Equal(j.t, expectedValue, value, msgAndArgs)
}

func (j *JsonAssert) AssertNodeEqualToTheFloat64(path string, expectedValue float64, msgAndArgs ...interface{}) {
	value := j.read(path)
	assert.IsType(j.t, 0.0, value, msgAndArgs)
	assert.Equal(j.t, expectedValue, value, msgAndArgs)
}

func (j *JsonAssert) AssertNodeIsTrue(path string, msgAndArgs ...interface{}) {
	value := j.read(path)
	assert.True(j.t, value.(bool), msgAndArgs)
}

func (j *JsonAssert) AssertNodeIsFalse(path string, msgAndArgs ...interface{}) {
	value := j.read(path)
	assert.False(j.t, value.(bool), msgAndArgs)
}

func (j *JsonAssert) AssertNodeIsNull(path string, msgAndArgs ...interface{}) {
	value := j.read(path)
	assert.Nil(j.t, value, msgAndArgs)
}

func (j *JsonAssert) read(path string) interface{} {
	value, err := jsonpath.Read(j.data, path)
	if err != nil {
		panic(fmt.Errorf("failed to read json path '%s': %s", path, err))
	}
	return value
}

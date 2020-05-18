package assertjson

import (
	"encoding/json"
	"fmt"
	"github.com/stretchr/testify/assert"
	"github.com/yalp/jsonpath"
	"math"
	"testing"
)

type AssertJSON struct {
	t    *testing.T
	data interface{}
}

type AssertNode struct {
	t     *testing.T
	err   error
	path  string
	value interface{}
}

type JSONAssertFunc func(json *AssertJSON)

func Has(t *testing.T, data []byte, jsonAssert JSONAssertFunc) {
	body := &AssertJSON{t: t}
	err := json.Unmarshal(data, &body.data)
	if err != nil {
		assert.Failf(t, "data has invalid JSON: %s", err.Error())
	} else {
		jsonAssert(body)
	}
}

func (j *AssertJSON) Node(path string) *AssertNode {
	value, err := jsonpath.Read(j.data, path)

	return &AssertNode{
		t:     j.t,
		err:   err,
		path:  path,
		value: value,
	}
}

func (node *AssertNode) exists() bool {
	if node.err != nil {
		assert.Fail(node.t, fmt.Sprintf("failed to find json node '%s': %v", node.path, node.err))
	}

	return node.err == nil
}

func (node *AssertNode) ShouldExist(msgAndArgs ...interface{}) {
	if node.err != nil {
		assert.Failf(node.t, "failed asserting that json node '%s' exists", node.path, msgAndArgs...)
	}
}

func (node *AssertNode) ShouldNotExist(msgAndArgs ...interface{}) {
	if node.err == nil {
		assert.Failf(node.t, "failed asserting that json node '%s' does not exist", node.path, msgAndArgs...)
	}
}

func (node *AssertNode) EqualToTheString(expectedValue string, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.IsType(node.t, "", node.value, msgAndArgs...)
		assert.Equal(node.t, expectedValue, node.value, msgAndArgs...)
	}
}

func (node *AssertNode) ShouldMatch(regexp string, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.IsType(node.t, "string", node.value, msgAndArgs...)
		assert.Regexp(node.t, regexp, node.value, msgAndArgs...)
	}
}

func (node *AssertNode) ShouldContain(contain string, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.Contains(node.t, node.value, contain, msgAndArgs...)
	}
}

func (node *AssertNode) ArrayShouldHaveElementsCount(count int, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.Len(node.t, node.value, count, msgAndArgs...)
	}
}

func (node *AssertNode) ShouldBeAStringWithLengthInRange(min int, max int, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.IsType(node.t, "string", node.value, msgAndArgs...)
		assert.GreaterOrEqual(node.t, len(node.value.(string)), min, msgAndArgs...)
		assert.LessOrEqual(node.t, len(node.value.(string)), max, msgAndArgs...)
	}
}

func (node *AssertNode) ShouldBeANumberInRange(min float64, max float64, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.GreaterOrEqual(node.t, node.value, min, msgAndArgs...)
		assert.LessOrEqual(node.t, node.value, max, msgAndArgs...)
	}
}

func (node *AssertNode) EqualToTheInteger(expectedValue int, msgAndArgs ...interface{}) {
	if node.exists() {
		float, ok := node.value.(float64)
		if !ok {
			assert.Failf(node.t, "value at path '%s' is not numeric", node.path, msgAndArgs...)
		}
		integer, fractional := math.Modf(float)
		if fractional != 0 {
			assert.Failf(node.t, "value at path '%s' is float, not integer", node.path, msgAndArgs...)
		}
		assert.Equal(node.t, expectedValue, int(integer), msgAndArgs...)
	}
}

func (node *AssertNode) EqualToTheFloat64(expectedValue float64, msgAndArgs ...interface{}) {
	if node.exists() {
		assert.IsType(node.t, 0.0, node.value, msgAndArgs...)
		assert.Equal(node.t, expectedValue, node.value, msgAndArgs...)
	}
}

func (node *AssertNode) IsTrue(msgAndArgs ...interface{}) {
	if node.exists() {
		assert.True(node.t, node.value.(bool), msgAndArgs...)
	}
}

func (node *AssertNode) IsFalse(msgAndArgs ...interface{}) {
	if node.exists() {
		assert.False(node.t, node.value.(bool), msgAndArgs...)
	}
}

func (node *AssertNode) IsNull(msgAndArgs ...interface{}) {
	if node.exists() {
		assert.Nil(node.t, node.value, msgAndArgs...)
	}
}

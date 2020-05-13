package xmlassert

import (
	"bytes"
	"fmt"
	"github.com/stretchr/testify/assert"
	"gopkg.in/xmlpath.v2"
	"testing"
)

type XMLAssert struct {
	t   *testing.T
	xml *xmlpath.Node
}

func Parse(t *testing.T, data []byte) (*XMLAssert, error) {
	xml, err := xmlpath.Parse(bytes.NewReader(data))
	body := &XMLAssert{
		t:   t,
		xml: xml,
	}

	return body, err
}

func MustParse(t *testing.T, data []byte) *XMLAssert {
	body, err := Parse(t, data)
	if err != nil {
		panic(err)
	}

	return body
}

func (x *XMLAssert) AssertNodeEqualToTheString(path string, expectedValue string, msgAndArgs ...interface{}) {
	value := x.mustRead(path)
	assert.Equal(x.t, expectedValue, value, msgAndArgs)
}

func (x *XMLAssert) mustRead(path string) string {
	p := xmlpath.MustCompile(path)
	value, ok := p.String(x.xml)
	if !ok {
		assert.Fail(x.t, fmt.Sprintf("failed to read xml path '%s'", path))
	}
	return value
}

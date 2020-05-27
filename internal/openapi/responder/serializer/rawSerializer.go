package serializer

import "fmt"

type rawSerializer struct{}

func (*rawSerializer) Serialize(data interface{}, format string) ([]byte, error) {
	if bytes, ok := data.([]byte); ok {
		return bytes, nil
	}
	if s, ok := data.(string); ok {
		return []byte(s), nil
	}
	if s, ok := data.(fmt.Stringer); ok {
		return []byte(s.String()), nil
	}

	return nil, ErrUnserializableData
}

package serializer

import "encoding/json"

type jsonSerializer struct{}

func (*jsonSerializer) Serialize(data interface{}, format string) ([]byte, error) {
	return json.Marshal(data)
}

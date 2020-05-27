package serializer

import "github.com/clbanning/mxj"

type xmlSerializer struct {
	rootTag string
}

func (serializer *xmlSerializer) Serialize(data interface{}, format string) ([]byte, error) {
	if object, isObject := data.(map[string]interface{}); isObject {
		xml := mxj.Map(object)
		return xml.Xml()
	}

	return mxj.AnyXml(data, serializer.rootTag)
}

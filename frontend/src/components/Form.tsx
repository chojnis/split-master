import { useState } from 'react';
import { View, Text, TextInput, StyleSheet, Button } from 'react-native';

export type FormFieldType = 'text' | 'number';

interface Field {
  label: string;
  name: string;
  type: FormFieldType;
}

interface FormProps {
  fields: Field[];
  onSubmit: (data: { [key: string]: string | number }) => void;
}

const Form = ({ fields, onSubmit }: FormProps) => {
  const [formData, setFormData] = useState<{ [key: string]: string | number }>({});

  const handleChange = (name: string, value: string | number) => {
    setFormData({ ...formData, [name]: value });
  };

  const handleSubmit = () => {
    onSubmit(formData);
  };

  return (
    <View style={styles.container}>
      {fields.map((field) => (
        <View key={field.name} style={styles.fieldContainer}>
          <Text style={styles.label}>{field.label}</Text>
          <TextInput
            style={styles.input}
            placeholder={field.label}
            keyboardType={field.type === 'number' ? 'number-pad' : 'default'}
            onChangeText={(value) => handleChange(field.name, value)}
            value={formData[field.name]?.toString()}
          />
        </View>
      ))}
      <Button title="Submit" onPress={handleSubmit} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
  },
  fieldContainer: {
    marginBottom: 10,
  },
  label: {
    fontSize: 16,
    marginBottom: 5,
  },
  input: {
    height: 40,
    borderColor: 'gray',
    borderWidth: 1,
    padding: 10,
  },
});

export default Form;

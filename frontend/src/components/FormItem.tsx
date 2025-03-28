import { Input } from '~/components/ui/input';
import { Textarea } from '~/components/ui/textarea';
import { Label } from '~/components/ui/label';
import { Text } from '~/components/ui/text';
import { View } from 'react-native';
import { FormFieldType } from '~/components/Form';

type FormFieldProps = {
  field: FormFieldType;
  value?: string;
  onChange: (name: string, value: string | number) => void;
  error?: string;
}

const FormField = ({ field, value, onChange, error }: FormFieldProps) => {
  const handleChange = (text: string) => {
    onChange(field.name, field.type === 'number' ? Number(text) : text);
  };

  return (
    <View>
        <Label
            className="mt-2"
            nativeID={field.name}
        >
            {field.label}
        </Label>
        {field.type === 'textarea' ? (
            <Textarea 
                value={value} 
                onChangeText={handleChange} 
                placeholder={field.placeholder} 
                keyboardType='default'
                aria-labelledby={field.name}
                className={error ? 'border-red-500' : ''}
            />
        ) : (
            <Input
                value={value}
                onChangeText={handleChange}
                keyboardType={field.type === 'number' ? 'number-pad' : 'default'}
                placeholder={field.placeholder}
                secureTextEntry={field.type === 'password'}
                className={error ? 'border-red-500' : ''}
                aria-labelledby={field.name}
            />
        )}
        {error !== undefined && <Text className="text-red-500">{error}</Text>}
    </View>
  );
};

export default FormField;

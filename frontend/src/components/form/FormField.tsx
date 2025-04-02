import { Input } from '~/components/ui/input';
import { Textarea } from '~/components/ui/textarea';
import { Label } from '~/components/ui/label';
import { Text } from '~/components/ui/text';
import { View } from 'react-native';
import { FormFieldType } from '~/components/form/Form';
import Select from '~/components/form/SelectField';

type FormFieldProps = {
  field: FormFieldType;
  value?: FormFieldValue;
  onChange: (name: string, value: FormFieldValue) => void;
  error?: string;
  className?: string;
}

export type FormFieldValue = string | string[] | number;

const FormField = ({ field, value, onChange, className, error }: FormFieldProps) => {
  const handleChange = (value: FormFieldValue) => {
    onChange(field.name, field.type === 'number' ? Number(value) : value);
  };

  return (
    <View className={`${className || ''}`}>
        <Label
            className="my-2"
            nativeID={field.name}
        >
            {field.label}
            {field.required && <Text className="text-red-500"> *</Text>}
        </Label>
        <View className={`border rounded-md relative flex justify-center ${error ? 'border-red-500' : 'border-stone-300'} ${field.type !== 'select' && field.type !== 'textarea' ? 'h-16' : ''}`}>
          {field.type === 'textarea' && (
            <Textarea 
              value={value !== undefined ? String(value) : undefined} 
              onChangeText={handleChange} 
              placeholder={field.placeholder} 
              keyboardType='default'
              aria-labelledby={field.name}
              className={'border-transparent'}
            />
          )}
          {field.type === 'select' && (
            <Select
              value={typeof value === 'number' ? String(value) : value as string | string[] | undefined}
              onChangeValue={handleChange}
              selectOptions={field.selectOptions || []}
              className={'border-transparent'}
              defaultValue={field.defaultSelectValue}
              multiple={field.multiple}
            />
          )}
          {field.type !== 'textarea' && field.type !== 'select' && (
            <Input
              value={value !== undefined ? String(value) : undefined}
              onChangeText={handleChange}
              keyboardType={field.type === 'number' ? 'number-pad' : 'default'}
              placeholder={field.placeholder}
              secureTextEntry={field.type === 'password'}
              className={'border-transparent'}
              aria-labelledby={field.name}
            />
          )}
        </View>
        {error !== undefined && <Text className="text-red-500">{error}</Text>}
    </View>
  );
};

export default FormField;

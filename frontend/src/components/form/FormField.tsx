import { Input } from '~/components/ui/input';
import { Textarea } from '~/components/ui/textarea';
import { Label } from '~/components/ui/label';
import { Text } from '~/components/ui/text';
import { View } from 'react-native';
import { FormFieldType } from '~/components/form/Form';
import Select from '~/components/form/SelectField';
import { useEffect } from 'react';

type FormFieldProps = {
  field: FormFieldType;
  value?: string | number | string[];
  onChange: (name: string, value: string | number | string[]) => void;
  error?: string;
  className?: string;
}

const FormField = ({ field, value, onChange, className, error }: FormFieldProps) => {
  const handleChange = (value: string | number | string[]) => {
    onChange(field.name, field.type === 'number' ? Number(value) : value);
  };

  // if(field.defaultSelectValue && value === undefined) {
  //   if(Array.isArray(field.defaultSelectValue)) {
  //     const defaultValues = field.defaultSelectValue.map((item) => item?.value);
  //     onChange(field.name, defaultValues);
  //     return;
  //   }

  //   onChange(field.name, field.defaultSelectValue.value);
  //   return;
  // }

  useEffect(() => {
    if (field.defaultSelectValue && value === undefined) {
      if (Array.isArray(field.defaultSelectValue)) {
        const defaultValues = field.defaultSelectValue.map((item) => item?.value);
        onChange(field.name, defaultValues);
      } else {
        onChange(field.name, field.defaultSelectValue.value);
      }
    }
  }, []);

  return (
    <View className={`${className || ''}`}>
        <Label
            className="mt-2"
            nativeID={field.name}
        >
            {field.label}
        </Label>
          {field.type === 'textarea' && (
            <Textarea 
              value={value !== undefined ? String(value) : undefined} 
              onChangeText={handleChange} 
              placeholder={field.placeholder} 
              keyboardType='default'
              aria-labelledby={field.name}
              className={error ? 'border-red-500' : ''}
            />
          )}
          {field.type === 'select' && (
            <Select
              value={typeof value === 'number' ? String(value) : value as string | string[] | undefined}
              onChangeValue={handleChange}
              selectOptions={field.selectOptions || []}
              className={error ? 'border-red-500' : ''}
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
              className={error ? 'border-red-500' : ''}
              aria-labelledby={field.name}
            />
          )}
        {error !== undefined && <Text className="text-red-500">{error}</Text>}
    </View>
  );
};

export default FormField;

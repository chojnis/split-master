import { Input } from '~/components/ui/input';
import { Textarea } from '~/components/ui/textarea';
import { Label } from '~/components/ui/label';
import { Text } from '~/components/ui/text';
import { View } from 'react-native';
import { FormFieldType } from '~/components/form/Form';
import Select from '~/components/form/SelectField';
import DateField from '~/components/form/DateField';
import { useEffect, useState } from 'react';

type FormFieldProps = {
  field: FormFieldType;
  value: FormFieldValue;
  onChange: (name: string, value: FormFieldValue) => void;
  error?: string;
  className?: string;
}

export type FormFieldValue = string | string[] | number | Date;

const FormField = ({ field, value, onChange, className, error }: FormFieldProps) => {
  const [localValue, setLocalValue] = useState<string>(value?.toString?.() || '');

  const handleChange = (value: FormFieldValue) => {
    if(field.type === 'number') {
      if (typeof value === 'string') {
        value = value.replace(/[^0-9.]/g, '');
        // value = parseFloat(value)
      }

      setLocalValue(value.toString());
    }

    onChange(field.name, value);
  };

  useEffect(() => {
    if (field.type === 'number') {
      setLocalValue((value ?? '').toString());
    }
  }, [value]);

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
              value={value as string} 
              onChangeText={handleChange} 
              placeholder={field.placeholder} 
              keyboardType='default'
              aria-labelledby={field.name}
              className={'border-transparent bg-transparent'}
            />
          )}
          {field.type === 'select' && (
            <Select
              // value={typeof value === 'number' ? String(value) : value as string | string[] | undefined}
              value={value}
              disabled={field.disabled}
              onChangeValue={handleChange}
              selectOptions={field.selectOptions || []}
              className={'border-transparent'}
              defaultValue={field.defaultSelectValue}
              multiple={field.multiple}
            />
          )}
          {field.type === 'date' && (
            <DateField
              value={value as Date}
              onChange={handleChange}
            />
          )}
          {field.type !== 'textarea' && field.type !== 'select' && (
            <Input
              value={field.type === 'number' ? localValue : value as string}
              onChangeText={handleChange}
              placeholder={field.placeholder}
              keyboardType={field.type === 'number' ? 'numeric' : 'default'}
              secureTextEntry={field.type === 'password'}
              aria-labelledby={field.name}
              className={'border-transparent bg-transparent'}
            />
          )}
        </View>
        {error !== undefined && <Text className="text-red-500">{error}</Text>}
        {field.description && (
          <Text className="text-sm text-gray-500 mt-1">
            {field.description}
          </Text>
        )}
    </View>
  );
};

export default FormField;

import { View } from 'react-native';
import {Picker} from '@react-native-picker/picker';
import { useColorScheme } from '~/lib/useColorScheme';
import CheckboxField from './CheckboxField';
import { FormFieldValue } from './FormField';

// export type SelectOption = Option;
export type SelectOption = {
    label: string;
    value: string;
};

type SelectProps = {
    value?: FormFieldValue;
    onChangeValue: (value: FormFieldValue) => void;
    selectOptions: SelectOption[];
    defaultValue?: SelectOption | SelectOption[];
    className?: string;
    multiple?: boolean;
}

const findOptionByValue = (options: SelectOption[], value?: string | number) => {
    return options.find(option => option.value == value);
}

const findOptionsByValue = (options: SelectOption[], values?: string[]) => {
    return options.filter(option => values?.includes(option.value));
}

const SelectField = ({
    value,
    onChangeValue, 
    className,
    selectOptions,
    defaultValue,
    multiple
}: SelectProps) => {
    const { colorScheme } = useColorScheme();

    return (
        <View className={`p-0 m-0 ${className || ''}`}>
            {!multiple ? (
                <Picker
                    selectedValue={value}
                    onValueChange={(itemValue, itemIndex) => {
                        const selectedOption = selectOptions[itemIndex];
                        if (selectedOption) {
                            onChangeValue(selectedOption.value);
                        }
                    }}
                    mode={"dropdown"}
                >
                {selectOptions.map((option) => (
                    <Picker.Item 
                        key={option.value} 
                        value={option.value} 
                        label={option.label} 
                        style={{
                            backgroundColor: colorScheme === 'dark' ? '#000' : '#fff',
                            color: colorScheme === 'dark' ? '#fff' : '#000',
                            fontSize: 16,
                        }}
                    />
                ))}
                </Picker>
            ) : (
                <CheckboxField
                    values={Array.isArray(value) ? value : []}
                    options={selectOptions}
                    onChange={(values) => {
                        onChangeValue(values);
                    }}
                />
            )}
        </View>
    );
}

export default SelectField;
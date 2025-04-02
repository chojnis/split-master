import { useEffect } from 'react';
import { TouchableOpacity, View, StyleSheet } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Option
} from '~/components/ui/select';
import { Text } from '~/components/ui/text';
import RNPickerSelect from 'react-native-picker-select';
import { MultiSelect } from 'react-native-element-dropdown';
import {Picker} from '@react-native-picker/picker';
import { useColorScheme } from '~/lib/useColorScheme';
import CheckboxField from './CheckboxField';

// export type SelectOption = Option;
export type SelectOption = {
    label: string;
    value: string;
};

type SelectProps = {
    value?: string | string[];
    onChangeValue: (value: string | string[]) => void;
    selectOptions: SelectOption[];
    defaultValue?: SelectOption | SelectOption[];
    className?: string;
    multiple?: boolean;
}

const findOptionByValue = (options: SelectOption[], value?: string) => {
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
    const selectedOption = multiple 
        ? findOptionsByValue(selectOptions, Array.isArray(value) ? value : [])
        : findOptionByValue(selectOptions, typeof value === 'string' ? value : undefined);
    const singleSelectedOption = multiple ? undefined : selectedOption as SelectOption | undefined;
    const singleDefaultValue = multiple ? undefined : defaultValue as SelectOption | undefined;

    const { colorScheme } = useColorScheme();

    return (
        <View className={`p-0 m-0 ${className || ''}`}>
            {!multiple ? (
                <Picker
                    selectedValue={singleSelectedOption}
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
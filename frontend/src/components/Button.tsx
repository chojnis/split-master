import { forwardRef } from 'react';
import { Text, TouchableOpacity, TouchableOpacityProps, View } from 'react-native';
import { StyleSheet } from 'react-native';

type ButtonProps = {
  title: string;
  link?: boolean;
} & TouchableOpacityProps;

export const Button = forwardRef<View, ButtonProps>(({ title, link = false, ...touchableProps }, ref) => {
  return (
    <TouchableOpacity
      ref={ref}
      {...touchableProps}
      className={`${touchableProps.className}`}
      style={link ? {} : styles.button}
      >
      <Text style={link ? {} : styles.buttonText}>{title}</Text>
    </TouchableOpacity>
  );
});

// const styles = {
//   button: 'items-center bg-indigo-500 rounded-[28px] shadow-md p-4',
//   buttonText: 'text-white text-lg font-semibold text-center',
// };

const styles = StyleSheet.create({
  button: {
    alignItems: 'center',
    backgroundColor: 'indigo',
    borderRadius: 28,
    shadowColor: 'black',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    padding: 4,
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
    textAlign: 'center',
  },
});

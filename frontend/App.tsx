import './global.css';

import { Provider } from 'react-redux';
import { PersistGate } from 'redux-persist/integration/react';
import { store, persistor } from './src/store';
import Navigation from './src/navigation';
import LoadingScreen from './src/screens/loading';

export default function App() {
  return (
      <Provider store={store}>
        <PersistGate loading={<LoadingScreen />} persistor={persistor}>
          <Navigation />
        </PersistGate>
      </Provider>
  );
}

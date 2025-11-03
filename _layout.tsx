import { Stack } from 'expo-router';

export default function RootLayout() {
  return (
    <Stack>
      {/* 👇 this line ensures the app starts at your (tabs) layout */}
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      {/* Optional modal route if you use one */}
      <Stack.Screen name="modal" options={{ presentation: 'modal' }} />
    </Stack>
  );
}

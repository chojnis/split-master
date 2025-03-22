import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import { RootState } from '~/store';
import { LoginResponse, RegisterResponse, GroupsResponse } from '~/api/response';
import { LoginRequest, RegisterRequest } from '~/api/request';

const BASE_URL = 'https://8822-217-97-63-46.ngrok-free.app/api/';

export const apiCall = createApi({
  reducerPath: 'api',
  baseQuery: fetchBaseQuery({
    baseUrl: BASE_URL,
    prepareHeaders: (headers, { getState }) => {
      const token = (getState() as RootState).auth.token;
      if (token) {
        headers.set('authorization', `Bearer ${token}`);
      }
      return headers;
    },
  }),
  endpoints: (builder) => ({
    login: builder.mutation<LoginResponse, LoginRequest>({
      query: (credentials) => ({
        url: 'auth',
        method: 'POST',
        body: credentials,
      }),
    }),
    register: builder.mutation<RegisterResponse, RegisterRequest>({
      query: (credentials) => ({
        url: 'register',
        method: 'POST',
        body: credentials,
      }),
    }),
    getGroups: builder.query<GroupsResponse, void>({
      query: () => 'groups',
    }),
  }),
});

export const { useLoginMutation, useRegisterMutation, useGetGroupsQuery } = apiCall;
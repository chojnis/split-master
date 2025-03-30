import { createApi } from '@reduxjs/toolkit/query/react';
import { 
  LoginResponse, 
  RegisterResponse, 
  GroupsResponse, 
  GroupMembersResponse,
  GroupTransactionResponse,
  AddGroupResponse,
  GroupResponse,
  UserResponse,
  CurrenciesResponse,
  AddTransactionResponse
} from '~/api/types/response';
import { 
  LoginRequest, 
  RegisterRequest,
  AddGroupRequest,
  AddTransactionRequest
} from '~/api/types/request';
import baseQuery from '~/api/query';

export const apiCall = createApi({
  reducerPath: 'api',
  baseQuery: baseQuery,
  refetchOnReconnect: true,
  endpoints: (builder) => ({
    login: builder.mutation<LoginResponse, LoginRequest>({
      query: (credentials) => ({
        url: 'login',
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
    getGroupMembers: builder.query<GroupMembersResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/members`,
        method: 'GET',
      }),
    }),
    getGroupTransactions: builder.query<GroupTransactionResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/transactions`,
        method: 'GET',
      }),
    }),
    addGroup: builder.mutation<AddGroupResponse, AddGroupRequest>({
      query: (data) => ({
        url: 'groups',
        method: 'POST',
        body: data,
      }),
    }),
    getGroup: builder.query<GroupResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}`,
        method: 'GET',
      }),
    }),
    getUser: builder.query<UserResponse, string>({
      query: (userId) => ({
        url: `users/${userId}`,
        method: 'GET',
      }),
    }),
    getCurrencies: builder.query<CurrenciesResponse, void>({
      query: () => ({
        url: 'currencies',
        method: 'GET',
      }),
    }),
    addTransaction: builder.mutation<AddTransactionResponse, AddTransactionRequest>({
      query: ({groupId, data}) => ({
        url: `groups/${groupId}/transactions`,
        method: 'POST',
        body: data,
      }),
    }),
  }),
});

export const { 
  useLoginMutation, 
  useRegisterMutation,
  useGetGroupsQuery, 
  useGetGroupMembersQuery,
  useGetGroupTransactionsQuery,
  useAddGroupMutation,
  useGetGroupQuery,
  useGetUserQuery,
  useGetCurrenciesQuery,
  useAddTransactionMutation,
} = apiCall;
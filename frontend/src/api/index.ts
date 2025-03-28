import { createApi } from '@reduxjs/toolkit/query/react';
import { 
  LoginResponse, 
  RegisterResponse, 
  GroupsResponse, 
  GroupMembersResponse,
  GroupTransactionResponse,
  AddGroupResponse
} from '~/api/types/response';
import { 
  LoginRequest, 
  RegisterRequest,
  AddGroupRequest
} from '~/api/types/request';
import baseQuery from '~/api/query';

export const apiCall = createApi({
  reducerPath: 'api',
  baseQuery: baseQuery,
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
  }),
});

export const { 
  useLoginMutation, 
  useRegisterMutation,
  useGetGroupsQuery, 
  useGetGroupMembersQuery,
  useGetGroupTransactionsQuery,
  useAddGroupMutation
} = apiCall;
import { BaseQueryFn, FetchArgs } from '@reduxjs/toolkit/query/react';

export type ApiError = {
    status: number | string;
    detail: string;
    violations?: Violation[];
};

export type Violation = {
    propertyPath: string;
    message: string;
}

export type ErrorBaseQueryFn = BaseQueryFn<
  string | FetchArgs,
  unknown,
  ApiError
>;
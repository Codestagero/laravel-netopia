<?php

namespace iRealWorlds\Netopia\Enums;

enum ExceptionCode: int
{
    case Approved = 0;
    case CardAtRisk = 16;
    case CardNumberIncorrect = 17;
    case CardClosed = 18;
    case CardExpired = 19;
    case InsufficientFunds = 20;
    case CvvCodeIncorrect = 21;
    case IssuerUnavailable = 22;
    case AmountIncorrect = 32;
    case CurrencyIncorrect = 33;
    case TransactionNotPermittedToCardholder = 34;
    case TransactionDeclinedGeneric = 35;
    case TransactionRejectedByAntiFraud = 36;
    case TransactionIllegal = 37;
    case TransactionDeclined = 38;
    case InvalidRequest = 48;
    case DuplicatePreauth = 49;
    case DuplicateAuth = 50;
    case CanOnlyCancelPreauth = 51;
    case CanOnlyConfirmPreauth = 52;
    case CanOnlyCreditConfirmed = 53;
    case CreditAmountHigherThanAuthAmount = 54;
    case CaptureAmountHigherThanPreauthAmount = 55;
    case DuplicateRequest = 56;
    case GenericError = 99;
}

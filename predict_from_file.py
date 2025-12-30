import sys
import json
import pickle
import pandas as pd
import numpy as np
import warnings
import os
warnings.filterwarnings('ignore')

try:
    # Get input file path from command line
    input_file = sys.argv[1]
    
    # Read input data from file
    with open(input_file, 'r') as f:
        input_data = json.load(f)
    
    # Load the model and scaler
    with open('rf_model.pkl', 'rb') as f:
        model = pickle.load(f)

    with open('scaler.pkl', 'rb') as f:
        scaler = pickle.load(f)

    with open('model_info.pkl', 'rb') as f:
        model_info = pickle.load(f)

    # Create DataFrame with exact column names from your dataset
    df = pd.DataFrame([input_data])

    # Get categorical columns (excluding continuous ones)
    categorical_cols = model_info['categorical_features']
    continuous_cols = model_info['continuous_features']

    # Apply one-hot encoding for categorical features
    df_encoded = pd.get_dummies(df, columns=categorical_cols, drop_first=True)

    # Ensure all features from training are present (add missing columns with 0)
    for col in model_info['feature_names']:
        if col not in df_encoded.columns:
            df_encoded[col] = 0

    # Reorder columns to match training data exactly
    df_encoded = df_encoded[model_info['feature_names']]

    # Scale continuous features
    if continuous_cols:
        df_encoded[continuous_cols] = scaler.transform(df_encoded[continuous_cols])

    # Make prediction
    prediction = model.predict(df_encoded)

    # Return prediction as JSON
    result = {
        'predicted_price': float(prediction[0]),
        'status': 'success'
    }

    print(json.dumps(result))

except Exception as e:
    # Return error as JSON
    error_result = {
        'status': 'error',
        'error': str(e)
    }
    print(json.dumps(error_result))
    sys.exit(1)
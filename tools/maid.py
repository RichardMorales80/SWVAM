import pandas as pd
from sqlalchemy import create_engine

excel_path = r"C:\Users\richa\Downloads\CPdescarga.xls"

engine = create_engine("mysql+pymysql://root:@localhost:3306/matthew")

with pd.ExcelFile(excel_path) as xl:
    hojas = xl.sheet_names
hojas.remove("Nota")

for x in hojas:
    df = pd.read_excel(
        excel_path,
        sheet_name=x,
        usecols="A:F",
        skiprows=0,
        nrows=None,
        header=0,
        dtype=str,
        engine="xlrd",
    )

    df.to_sql(
        name="cat_cp",
        con=engine,
        if_exists="append",  # append para acumular todas las hojas en una sola tabla
        index=False,
    )
    print(f"✓ {x} subida correctamente ({len(df)} filas)")
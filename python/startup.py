import os

def startup():
    """
    Creates necessary directories if they do not exist.
    """
    python_dir = os.path.dirname(os.path.abspath(__file__))

    exports_path = os.path.join(python_dir, "exports")
    metadata_path = os.path.join(python_dir, "metadata")

    if not os.path.exists(exports_path):
        os.makedirs(exports_path, 0775)

    if not os.path.exists(metadata_path):
        os.makedirs(metadata_path, 0775)
import React, {useEffect, useState} from "react";
import RecipeWidget from "../../common/components/Recipe";
import {Link, useParams} from "react-router-dom";

// Simple example of a React "smart" component
const Recipe = ({recipe, base}) => {
    const {id} = useParams();
    const [showRecipe, setShowRecipe] = useState(recipe);
    const [loading, setLoading] = useState(!recipe || recipe.id !== parseInt(id, 10));

    useEffect(() => {
        if (!loading) {
            return;
        }

        fetch(base + "/api/recipes/" + id)
            .then(response => response.json())
            .then(data => {
                setShowRecipe(data);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return <div>Loading...</div>;
    }

    return (
        <>
            <ol className="breadcrumb">
                <li>
                    <Link to="/">Recipes</Link>
                </li>
                <li className="active">{showRecipe.name}</li>
            </ol>
            <RecipeWidget recipe={showRecipe}/>
        </>
    );
}

export default Recipe;
